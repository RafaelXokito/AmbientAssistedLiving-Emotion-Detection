package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import jdk.internal.net.http.common.Pair;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.NotificationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.StatisticDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.NotificationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Notification;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.SecurityContext;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

@Path("statistics")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Client", "Administrator"})
public class StatisticService {


    @EJB
    private IterationBean iterationBean;private NotificationBean notificationBean;
    @EJB
    private PersonBean personBean;
    @Context
    private SecurityContext securityContext;

    @GET
    @Path("/")
    public Response getAllNotificationsWS(@HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();

        List<StatisticDTO> statistics = new ArrayList<>();
        if (securityContext.isUserInRole("Client")) {

            statistics.add(toDTO("Total of notifications", String.valueOf(notificationBean.getAllNotificationsByClient(clientEmail, "false").size())));

            List<Pair<String, Integer>> listPair = notificationBean.getEmotionsWithMostNotificationsByClient(clientEmail);
            statistics.add(toDTOExtended("Emotion with the most notifications", listPair.get(0).first, String.valueOf(listPair.get(0).second)));

            statistics.add(toDTO("Last Iteration Time", String.valueOf(iterationBean.getLastIterationByClient(clientEmail).getCreated_at().getTime())));

            List<Pair<String, Integer>> listPair2 = notificationBean.getEmotionWithTheLeastNotificationsConfiguredByClient(clientEmail);
            statistics.add(toDTOExtended("Emotion with the least notifications configured", listPair2.get(0).first, String.valueOf(listPair2.get(0).second)));

            return Response.status(Response.Status.OK)
                    .entity(statistics)
                    .build();
        }

        statistics.add(toDTO("Total of notifications",String.valueOf(notificationBean.getAllNotifications( "false").size())));

        List<Pair<String, Integer>> listPair = notificationBean.getEmotionsWithMostNotifications();
        statistics.add(toDTOExtended("Emotion with the most notifications", listPair.get(0).first, String.valueOf(listPair.get(0).second)));

        statistics.add(toDTO("Last Iteration Time", String.valueOf(iterationBean.getLastIteration().getCreated_at().getTime())));

        List<Pair<String, Integer>> listPair2 = notificationBean.getEmotionWithTheLeastNotificationsConfigured();
        statistics.add(toDTOExtended("Emotion with the least notifications configured", listPair2.get(0).first, String.valueOf(listPair2.get(0).second)));

        return Response.status(Response.Status.OK)
                .entity(statistics)
                .build();
    }

    private StatisticDTO toDTOExtended(String name, String value, String subValue){
        return new StatisticDTO(name, value, subValue);
    }

    private StatisticDTO toDTO(String name, String value){
        return new StatisticDTO(name, value);
    }
}
