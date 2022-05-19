package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.EmotionDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.EmotionNotificationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.EmotionNotificationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.EmotionNotification;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyUnauthorizedException;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.SecurityContext;
import java.time.Duration;
import java.util.List;
import java.util.stream.Collectors;

@Path("emotionsNotification")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Client", "Administrator"})
public class EmotionNotificationService {
    @EJB
    private EmotionNotificationBean emotionNotificationBean;
    @EJB
    private PersonBean personBean;

    @Context
    private SecurityContext securityContext;
    @GET
    @Path("/")
    public Response getAllEmotionsNotificationWS(@HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();

        if (securityContext.isUserInRole("Client"))
            return Response.status(Response.Status.OK)
                    .entity(simpleToDTOs(emotionNotificationBean.getAllEmotionsNotificationByClient(clientEmail)))
                    .build();

        return Response.status(Response.Status.OK)
                .entity(simpleToDTOs(emotionNotificationBean.getAllEmotionNotifications()))
                .build();
    }

    @GET
    @Path("{id}")
    public Response getEmotionNotificationWS(@PathParam("id") Long id, @HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        EmotionNotification emotionNotification = emotionNotificationBean.findEmotionNotification(id);

        if (securityContext.isUserInRole("Client") && !emotionNotification.getClient().getEmail().equals(clientEmail))
            throw new MyUnauthorizedException("You are not allowed to see this emotion notification");

        return Response.status(Response.Status.OK)
                .entity(toDTO(emotionNotification))
                .build();
    }

    @POST
    @Path("/")
    @RolesAllowed({"Client"})
    public Response createEmotionNotificationWS(EmotionNotificationDTO emotionNotificationDTO, @HeaderParam("Authorization") String auth) throws Exception{
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        Long id = emotionNotificationBean.create(emotionNotificationDTO.getAccuracyLimit(),emotionNotificationDTO.getDurationSeconds(), emotionNotificationDTO.getEmotion_name(),clientEmail);
        EmotionNotification emotionNotification = emotionNotificationBean.findEmotionNotification(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(emotionNotification))
                .build();
    }

    @DELETE
    @Path("{id}")
    @RolesAllowed({"Client"})
    public Response deleteEmotionNotificationWS(@PathParam("id") Long id,  @HeaderParam("Authorization") String auth) throws Exception{
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        EmotionNotification emotionNotification = emotionNotificationBean.findEmotionNotification(id);

        if(!emotionNotification.getClient().getEmail().equals(clientEmail)){
            throw new MyUnauthorizedException("You are not allowed to see this emotion notification");
        }
        if(emotionNotificationBean.delete(id)){
            return Response.status(Response.Status.OK)
                    .entity("[Success] Emotion Notification with id \'"+id+"\' was deleted")
                    .build();
        }
        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }
    private List<EmotionNotificationDTO> simpleToDTOs(List<EmotionNotification> emotionsNotification) {
        return emotionsNotification.stream().map(this::toDTO).collect(Collectors.toList());
    }


    private ClientDTO clientToDTO(Client client) {
        return new ClientDTO(
                client.getEmail(),
                client.getName(),
                client.getBirthDate(),
                client.getContact()
        );
    }

    EmotionDTO emotionToDTO(Emotion emotion) {
        return new EmotionDTO(
                emotion.getName(),
                emotion.getGroup()
        );
    }

    private EmotionNotificationDTO toDTO(EmotionNotification emotionNotification) {
        return new EmotionNotificationDTO(
                emotionNotification.getId(),
                emotionNotification.getEmotion().getName(),
                emotionNotification.getClient().getEmail(),
                emotionNotification.getAccuracyLimit(),
                emotionNotification.getDuration().getSeconds()
        );
    }
}
