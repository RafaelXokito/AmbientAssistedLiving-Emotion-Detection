package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.EmotionDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.NotificationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.NotificationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Notification;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyUnauthorizedException;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.SecurityContext;
import java.util.List;
import java.util.stream.Collectors;

@Path("notifications")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Client", "Administrator"})
public class NotificationService {

    @EJB
    private NotificationBean notificationBean;
    @EJB
    private PersonBean personBean;
    @Context
    private SecurityContext securityContext;

    @GET
    @Path("/")
    public Response getAllNotificationsWS(@HeaderParam("Authorization") String auth, @DefaultValue("yes") @QueryParam("is-short")  String isShort) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();

        if (securityContext.isUserInRole("Client"))
            return Response.status(Response.Status.OK)
                    .entity(toDTOs(notificationBean.getAllNotificationsByClient(clientEmail, isShort)))
                    .build();

        return Response.status(Response.Status.OK)
                .entity(toDTOs(notificationBean.getAllNotifications(isShort)))
                .build();
    }


    @GET
    @Path("{id}")
    public Response getNotificationWS(@PathParam("id") Long id,@HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        Notification notification = notificationBean.findNotification(id);

        if (securityContext.isUserInRole("Client") && !notification.getClient().getEmail().equals(clientEmail))
            throw new MyUnauthorizedException("You are not allowed to see this notification");

        return Response.status(Response.Status.OK)
                .entity(toDTO(notification))
                .build();
    }

    @PATCH
    @Path("{id}")
    @RolesAllowed({"Client"})
    public Response updateVisibleStatusWS(@PathParam("id") Long id,@HeaderParam("Authorization") String auth) throws Exception{
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        Notification notification = notificationBean.findNotification(id);
        if (securityContext.isUserInRole("Client") && !notification.getClient().getEmail().equals(clientEmail))
            throw new MyUnauthorizedException("You are not allowed to see this notification");
        notificationBean.updateVisibleStatus(id);
        return Response.status(Response.Status.OK)
                    .entity("[Success] Notification with id \'"+id+"\' was deleted")
                    .build();

    }

    private List<NotificationDTO> toDTOs(List<Notification> notifications){
        return notifications.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private NotificationDTO toDTO(Notification notification){
        return new NotificationDTO(notification.getId(),
                notification.getClient().getEmail(),
                notification.getTitle(),
                notification.getContent(),
                notification.getCreated_at(),
                notification.getNotificationSeen(),
                emotionToDTO(notification.getEmotion()),
                notification.getAccuracy(),
                notification.getDuration());
    }

    EmotionDTO emotionToDTO(Emotion emotion) {
        return new EmotionDTO(
                emotion.getName(),
                emotion.getGroup()
        );
    }
}
